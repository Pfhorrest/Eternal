-- Uniform.lua by Gregory Smith
-- Random points in Aleph One's game world
--
-- you may freely redistribute this file and/or include it in your scripts
--
-- to find a random x,y,polygon or x,y,z,polygon in a list of polygons:
-- uniform.build_triangle_list(polygon_list)
-- uniform.xy_in_triangle_list(triangle_list)
-- uniform.xyz_in_triangle_list(triangle_list)
--
-- inefficient convenience functions:
-- uniform.xy_in_polygon(polygon)
-- uniform.xyz_in_polygon(polygon)

uniform = {}

function uniform.xy_in_polygon(polygon)
   return uniform.xy_in_triangle_list(build_triangle_list({polygon}))
end

function uniform.xyz_in_polygon(polygon)
   return uniform.xyz_in_triangle_list(build_triangle_list({polygon}))
end

function uniform.build_triangle_list(polygon_list)
   local triangles = {}
   local total_area = 0
   for _, p in pairs(polygon_list) do
      local polygon_triangles = uniform.split_polygon_into_triangles(p)
      for _, t in pairs(polygon_triangles) do
	 table.insert(triangles, t)
	 total_area = total_area + t.area
      end
   end

   local area = 0
   for _, t in pairs(triangles) do
      area = area + t.area / total_area
      t.weight = area
   end

   return triangles
end

function uniform.xy_in_triangle_list(triangles)
   local t = uniform.search(triangles, uniform.random())
   local x, y = uniform.xy_in_triangle(t)
   return x, y, t.polygon
end

function uniform.xyz_in_triangle_list(triangles)
   local x, y, p = uniform.xy_in_triangle_list(triangles)
   local z = p.floor.height + uniform.random() * (p.ceiling.height - p.floor.height)
   return x, y, z, p
end

function uniform.split_polygon_into_triangles(p)
   local triangles = {}
   local endpoints = p.endpoints
   local first = endpoints[0]
   for i = 1, # p.endpoints - 2 do
      local a = first
      local b = endpoints[i]
      local c = endpoints[i + 1]
      local triangle = {a, b, c}
      triangle.area = math.abs((a.x * b.y - b.x * a.y) + (b.x * c.y - c.x * b.y) + (c.x * a.y - a.x * c.y)) / 2
      triangle.polygon = p
      
      table.insert(triangles, triangle)
   end
   return triangles
end

function uniform.random()
   return Game.random() / (2 ^ 32)
end

function uniform.xy_in_triangle(t)
   local a = uniform.random()
   local b = uniform.random()
   if a + b > 1 then
      a = 1 - a
      b = 1 - b
   end
   local c = 1 - a - b

   local ta = t[1]
   local tb = t[2]
   local tc = t[3]

   local x = ta.x * a + tb.x * b + tc.x * c
   local y = ta.y * a + tb.y * b + tc.y * c

   return x, y
end

function uniform.search(triangles, weight)
   local function search(low, high)
      local mid = math.floor((low + high) / 2)
      if low == high then
	 return high
      elseif triangles[mid].weight > weight then
	 return search(low, mid)
      else
	 return search(mid + 1, high)
      end
   end
   return triangles[search(1, # triangles)]
end


   